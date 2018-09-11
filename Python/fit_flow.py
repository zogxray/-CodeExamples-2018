from __future__ import print_function
from keras.models import Sequential
import pandas as pd
import pickle

import keras
from keras.preprocessing.text import Tokenizer
from keras.models import Sequential
from keras.layers import Dense
from keras.layers import Activation
from keras.layers import Dropout

def load_data_from_arrays(strings, labels, train_test_split=0.9):
    data_size = len(strings)
    test_size = int(data_size - round(data_size * train_test_split))
    print("Test size: {}".format(test_size))

    print("\nTraining set:")
    x_train = strings[test_size:]
    print("\t - x_train: {}".format(len(x_train)))
    y_train = labels[test_size:]
    print("\t - y_train: {}".format(len(y_train)))

    print("\nTesting set:")
    x_test = strings[:test_size]
    print("\t - x_test: {}".format(len(x_test)))
    y_test = labels[:test_size]
    print("\t - y_test: {}".format(len(y_test)))

    return x_train, y_train, x_test, y_test

df = pd.read_pickle('data/fit/dataset_flow.pickle')

print('Data loaded...')

total_categories = len(df[u'category'].unique())

data = df['body']
categories = df[u'category']

tokenizer = Tokenizer()
tokenizer.fit_on_texts(data.tolist())
textSequences = tokenizer.texts_to_sequences(data.tolist())

X_train, y_train, X_test, y_test = load_data_from_arrays(textSequences, categories, train_test_split=0.8)

total_words = len(tokenizer.word_index)

print('Dataset {} words'.format(total_words))
X_train = tokenizer.sequences_to_matrix(X_train, mode='binary')
X_test = tokenizer.sequences_to_matrix(X_test, mode='binary')

print(u'Categories to binary matrix '
      u'(for categorical_crossentropy)')
y_train = keras.utils.to_categorical(y_train, total_categories)
y_test = keras.utils.to_categorical(y_test, total_categories)


model = Sequential()
model.add(Dense(512, input_shape=(total_words+1,)))
model.add(Activation('relu'))
model.add(Dropout(0.4))
model.add(Dense(3000))
model.add(Activation('relu'))
model.add(Dropout(0.4))
model.add(Dense(10))
model.add(Dense(total_categories))
model.add(Activation('sigmoid'))

model.compile(loss='categorical_crossentropy',
              optimizer='adadelta'
                        '',
              metrics=['accuracy'])

print(model.summary())

epochs = 50

model.fit(X_train, y_train,
                    batch_size=32,
                    epochs=epochs,
                    verbose=1,
                    validation_split=0.1)

score = model.evaluate(X_test, y_test,
                       batch_size=32, verbose=1)
print()
print(u'Test score: {}'.format(score[0]))
print(u'Model accuracy: {}'.format(score[1]))

model_json = model.to_json()
with open("data/model/model_flow.json", "w") as json_file:
    json_file.write(model_json)
model.save_weights("data/model/model_flow.h5")
print("Saved model to disk")

# saving
with open('data/model/tokenizer_flow.pickle', 'wb') as handle:
    pickle.dump(tokenizer, handle, protocol=pickle.HIGHEST_PROTOCOL)
print("Saved tokenizer to disk")
